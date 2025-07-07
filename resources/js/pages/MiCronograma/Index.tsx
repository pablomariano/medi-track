import React, { useState } from 'react';
import { Link, router } from '@inertiajs/react';
import { useAuth } from '@/hooks/use-auth';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Calendar, Clock, CheckCircle, XCircle, AlertCircle, Pill, User, ArrowRight } from 'lucide-react';
import AppSidebarLayout from '@/layouts/app/app-sidebar-layout';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Textarea } from '@/components/ui/textarea';
import { Label } from '@/components/ui/label';

interface Administracion {
  id: number;
  fecha_hora_programada: string;
  fecha_hora_administrada: string | null;
  dosis_administrada: number;
  unidad_dosis: string;
  estado: 'pendiente' | 'administrado' | 'omitido';
  observaciones: string | null;
  medicamento: {
    id: number;
    nombre: string;
    principio_activo: string;
  };
  tratamiento: {
    id: number;
    nombre: string;
  };
}

interface Props {
  cronograma?: Administracion[];
  fecha?: string;
  estadisticas?: {
    total: number;
    administradas: number;
    pendientes: number;
    omitidas: number;
    cumplimiento: number;
  };
  fechas_disponibles?: Array<{
    fecha: string;
    label: string;
    es_hoy: boolean;
  }>;
}

export default function MiCronograma({ cronograma = [], fecha = new Date().toISOString().split('T')[0], estadisticas, fechas_disponibles = [] }: Props) {
  const auth = useAuth();
  const user = auth.user;
  
  const [fechaSeleccionada, setFechaSeleccionada] = useState(fecha);
  const [dialogType, setDialogType] = useState<'confirmar' | 'omitir' | null>(null);
  const [selectedAdministracion, setSelectedAdministracion] = useState<Administracion | null>(null);
  const [observaciones, setObservaciones] = useState('');
  const [efectosObservados, setEfectosObservados] = useState('');
  const [motivo, setMotivo] = useState('');
  const [processing, setProcessing] = useState(false);

  // Función para formatear hora
  const formatearHora = (fechaHora: string) => {
    return new Date(fechaHora).toLocaleTimeString('es-CL', { 
      hour: '2-digit', 
      minute: '2-digit' 
    });
  };

  // Función para formatear fecha completa
  const formatearFecha = (fecha: string) => {
    return new Date(fecha).toLocaleDateString('es-CL', {
      weekday: 'long',
      year: 'numeric',
      month: 'long',
      day: 'numeric'
    });
  };

  // Obtener color según estado
  const obtenerColorEstado = (estado: string) => {
    switch (estado) {
      case 'administrado':
        return 'bg-green-100 text-green-800 border-green-200';
      case 'pendiente':
        return 'bg-yellow-100 text-yellow-800 border-yellow-200';
      case 'omitido':
        return 'bg-red-100 text-red-800 border-red-200';
      default:
        return 'bg-gray-100 text-gray-800 border-gray-200';
    }
  };

  // Obtener icono según estado
  const obtenerIconoEstado = (estado: string) => {
    switch (estado) {
      case 'administrado':
        return <CheckCircle className="h-5 w-5 text-green-600" />;
      case 'pendiente':
        return <Clock className="h-5 w-5 text-yellow-600" />;
      case 'omitido':
        return <XCircle className="h-5 w-5 text-red-600" />;
      default:
        return <AlertCircle className="h-5 w-5 text-gray-600" />;
    }
  };

  // Agrupar por estado
  const administracionesPendientes = cronograma.filter(a => a.estado === 'pendiente');
  const administracionesCompletadas = cronograma.filter(a => a.estado === 'administrado');
  const administracionesOmitidas = cronograma.filter(a => a.estado === 'omitido');

  // Ordenar por hora
  const ordenarPorHora = (administraciones: Administracion[]) => {
    return administraciones.sort((a, b) => 
      new Date(a.fecha_hora_programada).getTime() - new Date(b.fecha_hora_programada).getTime()
    );
  };

  // Funciones para manejar administraciones
  const abrirDialogoConfirmar = (administracion: Administracion) => {
    setSelectedAdministracion(administracion);
    setDialogType('confirmar');
    setObservaciones('');
    setEfectosObservados('');
  };

  const abrirDialogoOmitir = (administracion: Administracion) => {
    setSelectedAdministracion(administracion);
    setDialogType('omitir');
    setMotivo('');
  };

  const cerrarDialog = () => {
    setDialogType(null);
    setSelectedAdministracion(null);
    setObservaciones('');
    setEfectosObservados('');
    setMotivo('');
  };

  const confirmarAdministracion = () => {
    if (!selectedAdministracion) return;

    setProcessing(true);
    
    router.post(route('mi-cronograma.confirmar-administracion', selectedAdministracion.id), {
      observaciones,
      efectos_observados: efectosObservados,
    }, {
      onSuccess: () => {
        cerrarDialog();
      },
      onError: (errors) => {
        console.error('Error al confirmar administración:', errors);
      },
      onFinish: () => {
        setProcessing(false);
      }
    });
  };

  const omitirAdministracion = () => {
    if (!selectedAdministracion) return;

    setProcessing(true);
    
    router.post(route('mi-cronograma.omitir-administracion', selectedAdministracion.id), {
      motivo,
    }, {
      onSuccess: () => {
        cerrarDialog();
      },
      onError: (errors) => {
        console.error('Error al omitir administración:', errors);
      },
      onFinish: () => {
        setProcessing(false);
      }
    });
  };

  // Manejar cambio de fecha
  const handleFechaChange = (nuevaFecha: string) => {
    setFechaSeleccionada(nuevaFecha);
    router.get(route('mi-cronograma.index'), { fecha: nuevaFecha }, { preserveState: false });
  };

  return (
    <AppSidebarLayout>
      <div className="container mx-auto py-6 max-w-6xl">
        {/* Header */}
        <div className="mb-6">
          <div className="flex items-center justify-between">
            <div>
              <h1 className="text-3xl font-bold text-gray-900">
                Mi Cronograma
              </h1>
              <p className="text-gray-600 mt-2">
                Horarios de medicamentos para {formatearFecha(fechaSeleccionada)}
              </p>
            </div>
            <div className="flex items-center gap-4">
              <Select value={fechaSeleccionada} onValueChange={handleFechaChange}>
                <SelectTrigger className="w-48">
                  <Calendar className="h-4 w-4 mr-2" />
                  <SelectValue placeholder="Seleccionar fecha" />
                </SelectTrigger>
                <SelectContent>
                  {fechas_disponibles.length > 0 ? (
                    fechas_disponibles.map((fecha) => (
                      <SelectItem key={fecha.fecha} value={fecha.fecha}>
                        {fecha.label} {fecha.es_hoy && '(Hoy)'}
                      </SelectItem>
                    ))
                  ) : (
                    /* Fallback: generar últimos 7 días */
                    Array.from({ length: 7 }, (_, i) => {
                      const date = new Date();
                      date.setDate(date.getDate() - i);
                      const dateString = date.toISOString().split('T')[0];
                      return (
                        <SelectItem key={dateString} value={dateString}>
                          {date.toLocaleDateString('es-CL')}
                        </SelectItem>
                      );
                    })
                  )}
                </SelectContent>
              </Select>
            </div>
          </div>
        </div>

        {/* Estadísticas */}
        {estadisticas && (
          <div className="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <Card>
              <CardContent className="pt-6">
                <div className="flex items-center justify-between">
                  <div>
                    <div className="text-2xl font-bold">{estadisticas.total}</div>
                    <p className="text-xs text-muted-foreground">Total del día</p>
                  </div>
                  <Pill className="h-8 w-8 text-muted-foreground" />
                </div>
              </CardContent>
            </Card>
            <Card>
              <CardContent className="pt-6">
                <div className="flex items-center justify-between">
                  <div>
                    <div className="text-2xl font-bold text-green-600">{estadisticas.administradas}</div>
                    <p className="text-xs text-muted-foreground">Administradas</p>
                  </div>
                  <CheckCircle className="h-8 w-8 text-green-600" />
                </div>
              </CardContent>
            </Card>
            <Card>
              <CardContent className="pt-6">
                <div className="flex items-center justify-between">
                  <div>
                    <div className="text-2xl font-bold text-yellow-600">{estadisticas.pendientes}</div>
                    <p className="text-xs text-muted-foreground">Pendientes</p>
                  </div>
                  <Clock className="h-8 w-8 text-yellow-600" />
                </div>
              </CardContent>
            </Card>
            <Card>
              <CardContent className="pt-6">
                <div className="flex items-center justify-between">
                  <div>
                    <div className="text-2xl font-bold">{estadisticas.cumplimiento}%</div>
                    <p className="text-xs text-muted-foreground">Cumplimiento</p>
                  </div>
                  <div className={`h-8 w-8 rounded-full flex items-center justify-center ${
                    estadisticas.cumplimiento >= 80 ? 'bg-green-100' : 
                    estadisticas.cumplimiento >= 60 ? 'bg-yellow-100' : 'bg-red-100'
                  }`}>
                    <span className={`text-sm font-bold ${
                      estadisticas.cumplimiento >= 80 ? 'text-green-600' : 
                      estadisticas.cumplimiento >= 60 ? 'text-yellow-600' : 'text-red-600'
                    }`}>
                      {Math.round(estadisticas.cumplimiento)}
                    </span>
                  </div>
                </div>
              </CardContent>
            </Card>
          </div>
        )}

        {/* Sin cronograma */}
        {cronograma.length === 0 && (
          <Card>
            <CardContent className="text-center py-12">
              <Calendar className="mx-auto h-12 w-12 text-muted-foreground mb-4" />
              <h3 className="text-lg font-medium text-muted-foreground mb-2">
                No hay medicamentos programados
              </h3>
              <p className="text-sm text-muted-foreground mb-6">
                No tienes medicamentos programados para esta fecha.
              </p>
              <Link href="/mis-tratamientos">
                <Button>
                  <Pill className="h-4 w-4 mr-2" />
                  Ver mis tratamientos
                </Button>
              </Link>
            </CardContent>
          </Card>
        )}

        {/* Medicamentos pendientes */}
        {administracionesPendientes.length > 0 && (
          <Card className="mb-6">
            <CardHeader>
              <CardTitle className="flex items-center gap-2">
                <Clock className="h-5 w-5 text-yellow-600" />
                Próximos Medicamentos
              </CardTitle>
              <CardDescription>
                Medicamentos que necesitas tomar próximamente
              </CardDescription>
            </CardHeader>
            <CardContent className="space-y-4">
              {ordenarPorHora(administracionesPendientes).map((administracion) => (
                <div key={administracion.id} className="flex items-center justify-between p-4 border rounded-lg bg-yellow-50">
                  <div className="flex items-center gap-4">
                    <div className="flex items-center gap-2">
                      {obtenerIconoEstado(administracion.estado)}
                      <span className="font-medium text-lg">
                        {formatearHora(administracion.fecha_hora_programada)}
                      </span>
                    </div>
                    <div className="flex-1">
                      <h4 className="font-medium">{administracion.medicamento.nombre}</h4>
                      <p className="text-sm text-muted-foreground">
                        {administracion.dosis_administrada} {administracion.unidad_dosis}
                      </p>
                      <p className="text-xs text-muted-foreground">
                        {administracion.tratamiento.nombre}
                      </p>
                    </div>
                  </div>
                  <div className="flex items-center gap-3">
                    <Button
                      size="sm"
                      variant="outline"
                      onClick={() => abrirDialogoOmitir(administracion)}
                      className="text-red-600 border-red-200 hover:bg-red-50"
                    >
                      <XCircle className="h-4 w-4 mr-1" />
                      Omitir
                    </Button>
                    <Button
                      size="sm"
                      onClick={() => abrirDialogoConfirmar(administracion)}
                      className="bg-green-600 hover:bg-green-700"
                    >
                      <CheckCircle className="h-4 w-4 mr-1" />
                      Confirmar
                    </Button>
                  </div>
                </div>
              ))}
            </CardContent>
          </Card>
        )}

        {/* Medicamentos completados */}
        {administracionesCompletadas.length > 0 && (
          <Card className="mb-6">
            <CardHeader>
              <CardTitle className="flex items-center gap-2">
                <CheckCircle className="h-5 w-5 text-green-600" />
                Medicamentos Completados
              </CardTitle>
              <CardDescription>
                Medicamentos que ya has tomado hoy
              </CardDescription>
            </CardHeader>
            <CardContent className="space-y-4">
              {ordenarPorHora(administracionesCompletadas).map((administracion) => (
                <div key={administracion.id} className="flex items-center justify-between p-4 border rounded-lg bg-green-50">
                  <div className="flex items-center gap-4">
                    <div className="flex items-center gap-2">
                      {obtenerIconoEstado(administracion.estado)}
                      <span className="font-medium text-lg">
                        {formatearHora(administracion.fecha_hora_programada)}
                      </span>
                    </div>
                    <div>
                      <h4 className="font-medium">{administracion.medicamento.nombre}</h4>
                      <p className="text-sm text-muted-foreground">
                        {administracion.dosis_administrada} {administracion.unidad_dosis}
                      </p>
                      {administracion.fecha_hora_administrada && (
                        <p className="text-xs text-green-600">
                          Tomado a las {formatearHora(administracion.fecha_hora_administrada)}
                        </p>
                      )}
                    </div>
                  </div>
                  <Badge className={obtenerColorEstado(administracion.estado)}>
                    Completado
                  </Badge>
                </div>
              ))}
            </CardContent>
          </Card>
        )}

        {/* Medicamentos omitidos */}
        {administracionesOmitidas.length > 0 && (
          <Card className="mb-6">
            <CardHeader>
              <CardTitle className="flex items-center gap-2">
                <XCircle className="h-5 w-5 text-red-600" />
                Medicamentos Omitidos
              </CardTitle>
              <CardDescription>
                Medicamentos que no se pudieron tomar
              </CardDescription>
            </CardHeader>
            <CardContent className="space-y-4">
              {ordenarPorHora(administracionesOmitidas).map((administracion) => (
                <div key={administracion.id} className="flex items-center justify-between p-4 border rounded-lg bg-red-50">
                  <div className="flex items-center gap-4">
                    <div className="flex items-center gap-2">
                      {obtenerIconoEstado(administracion.estado)}
                      <span className="font-medium text-lg">
                        {formatearHora(administracion.fecha_hora_programada)}
                      </span>
                    </div>
                    <div>
                      <h4 className="font-medium">{administracion.medicamento.nombre}</h4>
                      <p className="text-sm text-muted-foreground">
                        {administracion.dosis_administrada} {administracion.unidad_dosis}
                      </p>
                      {administracion.observaciones && (
                        <p className="text-xs text-red-600">
                          {administracion.observaciones}
                        </p>
                      )}
                    </div>
                  </div>
                  <Badge className={obtenerColorEstado(administracion.estado)}>
                    Omitido
                  </Badge>
                </div>
              ))}
            </CardContent>
          </Card>
        )}

        {/* Enlaces útiles */}
        <Card>
          <CardHeader>
            <CardTitle>Enlaces útiles</CardTitle>
            <CardDescription>
              Acciones relacionadas con tu tratamiento
            </CardDescription>
          </CardHeader>
          <CardContent>
            <div className="grid md:grid-cols-2 gap-4">
              <Link href="/mis-tratamientos" className="block">
                <div className="p-4 border rounded-lg hover:bg-muted/50 transition-colors">
                  <div className="flex items-center gap-3">
                    <Pill className="h-5 w-5 text-primary" />
                    <div>
                      <h4 className="font-medium">Mis Tratamientos</h4>
                      <p className="text-sm text-muted-foreground">Ver todos mis tratamientos activos</p>
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
                      <p className="text-sm text-muted-foreground">Actualizar información personal</p>
                    </div>
                    <ArrowRight className="h-4 w-4 ml-auto text-muted-foreground" />
                  </div>
                </div>
              </Link>
            </div>
          </CardContent>
        </Card>
      </div>

      {/* Dialog para confirmar administración */}
      <Dialog open={dialogType === 'confirmar'} onOpenChange={cerrarDialog}>
        <DialogContent className="sm:max-w-[425px]">
          <DialogHeader>
            <DialogTitle>Confirmar Administración</DialogTitle>
            <DialogDescription>
              Confirma que has tomado tu medicamento: <strong>{selectedAdministracion?.medicamento.nombre}</strong>
            </DialogDescription>
          </DialogHeader>
          <div className="grid gap-4 py-4">
            <div className="grid gap-2">
              <Label htmlFor="observaciones">
                Observaciones (opcional)
              </Label>
              <Textarea
                id="observaciones"
                placeholder="¿Cómo te sientes? ¿Alguna observación?"
                value={observaciones}
                onChange={(e) => setObservaciones(e.target.value)}
                rows={3}
              />
            </div>
            <div className="grid gap-2">
              <Label htmlFor="efectos">
                Efectos observados (opcional)
              </Label>
              <Textarea
                id="efectos"
                placeholder="¿Notaste algún efecto secundario?"
                value={efectosObservados}
                onChange={(e) => setEfectosObservados(e.target.value)}
                rows={2}
              />
            </div>
          </div>
          <DialogFooter>
            <Button type="button" variant="outline" onClick={cerrarDialog}>
              Cancelar
            </Button>
            <Button 
              type="submit" 
              onClick={confirmarAdministracion}
              disabled={processing}
              className="bg-green-600 hover:bg-green-700"
            >
              {processing ? 'Confirmando...' : 'Confirmar Toma'}
            </Button>
          </DialogFooter>
        </DialogContent>
      </Dialog>

      {/* Dialog para omitir administración */}
      <Dialog open={dialogType === 'omitir'} onOpenChange={cerrarDialog}>
        <DialogContent className="sm:max-w-[425px]">
          <DialogHeader>
            <DialogTitle>Omitir Administración</DialogTitle>
            <DialogDescription>
              Indica por qué no puedes tomar: <strong>{selectedAdministracion?.medicamento.nombre}</strong>
            </DialogDescription>
          </DialogHeader>
          <div className="grid gap-4 py-4">
            <div className="grid gap-2">
              <Label htmlFor="motivo">
                Motivo por el cual omites la dosis <span className="text-red-500">*</span>
              </Label>
              <Textarea
                id="motivo"
                placeholder="Explica brevemente el motivo (ej: olvido, efectos secundarios, no tengo el medicamento, etc.)"
                value={motivo}
                onChange={(e) => setMotivo(e.target.value)}
                rows={3}
                required
              />
            </div>
          </div>
          <DialogFooter>
            <Button type="button" variant="outline" onClick={cerrarDialog}>
              Cancelar
            </Button>
            <Button 
              type="submit" 
              onClick={omitirAdministracion}
              disabled={processing || !motivo.trim()}
              variant="destructive"
            >
              {processing ? 'Omitiendo...' : 'Omitir Dosis'}
            </Button>
          </DialogFooter>
        </DialogContent>
      </Dialog>
    </AppSidebarLayout>
  );
} 